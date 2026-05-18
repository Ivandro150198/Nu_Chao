package com.ecommerce.cod.repository;

import com.ecommerce.cod.model.Pedido;
import com.ecommerce.cod.enums.StatusPedido;
import com.ecommerce.cod.enums.StatusPagamento;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Optional;

@Repository
public interface PedidoRepository extends JpaRepository<Pedido, Long> {

    List<Pedido> findByUsuarioIdOrderByCriadoEmDesc(Long usuarioId);

    List<Pedido> findByStatusPedidoOrderByCriadoEmAsc(StatusPedido statusPedido);

    List<Pedido> findByStatusPedidoAndStatusPagamentoOrderByCriadoEmAsc(StatusPedido statusPedido, StatusPagamento statusPagamento);

    @Query("SELECT p FROM Pedido p WHERE p.statusPedido = 'PENDENTE' ORDER BY p.criadoEm ASC")
    List<Pedido> buscarPedidosPendentes();

    @Query("SELECT p FROM Pedido p WHERE p.usuarioId = :usuarioId AND p.statusPedido IN :statuses ORDER BY p.criadoEm DESC")
    List<Pedido> buscarPorUsuarioEStatuses(@Param("usuarioId") Long usuarioId, @Param("statuses") List<StatusPedido> statuses);

    @Query("SELECT p FROM Pedido p WHERE p.zonaId = :zonaId AND p.statusPedido = 'PENDENTE' ORDER BY p.criadoEm ASC")
    List<Pedido> buscarPedidosPendentesPorZona(@Param("zonaId") Long zonaId);

    @Query("SELECT p FROM Pedido p WHERE p.criadoEm BETWEEN :inicio AND :fim ORDER BY p.criadoEm DESC")
    List<Pedido> buscarPorPeriodo(@Param("inicio") LocalDateTime inicio, @Param("fim") LocalDateTime fim);

    @Query("SELECT p FROM Pedido p WHERE p.statusPedido = :status AND p.criadoEm >= :data ORDER BY p.criadoEm DESC")
    List<Pedido> buscarPorStatusEDataMinima(@Param("status") StatusPedido status, @Param("data") LocalDateTime data);

    @Query("SELECT COUNT(p) FROM Pedido p WHERE p.statusPedido = :status")
    Long contarPorStatus(@Param("status") StatusPedido status);

    @Query("SELECT SUM(p.valorTotal) FROM Pedido p WHERE p.statusPedido = 'ENTREGUE' AND p.criadoEm BETWEEN :inicio AND :fim")
    Optional<Double> somarTotalEntreguesNoPeriodo(@Param("inicio") LocalDateTime inicio, @Param("fim") LocalDateTime fim);

    @Query("SELECT p FROM Pedido p WHERE p.metodoPagamento = :metodo AND p.statusPagamento = 'PENDENTE' ORDER BY p.criadoEm ASC")
    List<Pedido> buscarPorMetodoPagamentoPendente(@Param("metodo") String metodo);
}
