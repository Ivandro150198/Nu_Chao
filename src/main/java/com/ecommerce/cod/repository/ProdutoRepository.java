package com.ecommerce.cod.repository;

import com.ecommerce.cod.model.Produto;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.math.BigDecimal;
import java.util.List;

@Repository
public interface ProdutoRepository extends JpaRepository<Produto, Long> {

    List<Produto> findByAtivoTrueOrderByNomeAsc();

    List<Produto> findByAtivoTrueOrderByPrecoAsc();

    List<Produto> findByAtivoTrueAndStockLessThanEqual(Integer limite);

    @Query("SELECT p FROM Produto p WHERE p.ativo = true AND p.stock > 0 ORDER BY p.nome ASC")
    List<Produto> buscarDisponiveis();

    @Query("SELECT p FROM Produto p WHERE p.ativo = true AND (LOWER(p.nome) LIKE LOWER(CONCAT('%', :termo, '%')) OR LOWER(p.descricao) LIKE LOWER(CONCAT('%', :termo, '%')))")
    List<Produto> buscarPorTermo(@Param("termo") String termo);

    @Query("SELECT p FROM Produto p WHERE p.ativo = true AND p.preco BETWEEN :min AND :max ORDER BY p.preco ASC")
    List<Produto> buscarPorFaixaDePreco(@Param("min") BigDecimal min, @Param("max") BigDecimal max);

    @Query("SELECT p FROM Produto p WHERE p.ativo = true AND p.stock < :limite ORDER BY p.stock ASC")
    List<Produto> buscarComStockBaixo(@Param("limite") Integer limite);

    @Query("SELECT COUNT(p) FROM Produto p WHERE p.ativo = true")
    Long contarAtivos();
}
